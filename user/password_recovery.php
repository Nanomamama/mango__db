<?php
session_start();
require_once '../admin/db.php'; // ไฟล์การเชื่อมต่อฐานข้อมูล
// require_once 'mail_config.php'; // ไฟล์การตั้งค่าอีเมล

// ฟังก์ชันส่งอีเมลจริง
function sendVerificationCode($email, $code, $name) {
    global $mail;
    
    try {
        $mail->addAddress($email, $name);
        $mail->Subject = 'รหัสยืนยันสำหรับกู้คืนรหัสผ่าน';
        
        $message = "
        <html>
        <head>
            <title>รหัสยืนยันสำหรับกู้คืนรหัสผ่าน</title>
            <style>
                body { font-family: 'Kanit', sans-serif; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(to right, #143a2c, #0dc984); padding: 20px; color: white; text-align: center; }
                .content { padding: 30px; background-color: #f9f9f9; }
                .code { font-size: 32px; font-weight: bold; text-align: center; letter-spacing: 5px; color: #0dc984; margin: 20px 0; }
                .footer { text-align: center; margin-top: 30px; color: #666; font-size: 14px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>กู้คืนรหัสผ่าน</h2>
                </div>
                <div class='content'>
                    <p>สวัสดี <strong>$name</strong>,</p>
                    <p>คุณได้ขอทำการกู้คืนรหัสผ่าน บัญชีผู้ใช้ของคุณ</p>
                    <p>กรุณาใช้รหัสยืนยันด้านล่างเพื่อดำเนินการตั้งรหัสผ่านใหม่:</p>
                    <div class='code'>$code</div>
                    <p>รหัสยืนยันนี้จะมีอายุการใช้งาน 5 นาที</p>
                    <p>หากคุณไม่ได้ทำการร้องขอนี้ กรุณาเพิกเฉยต่ออีเมลนี้</p>
                </div>
                <div class='footer'>
                    <p>© 2023 บริการของเรา. สงวนลิขสิทธิ์</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        $mail->Body = $message;
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("อีเมลไม่สามารถส่งได้: {$mail->ErrorInfo}");
        return false;
    }
}

// ฟังก์ชันสร้างรหัส 6 หลัก
function generateVerificationCode() {
    return rand(100000, 999999);
}

// ตรวจสอบฟอร์มที่ส่งมา
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['email'])) {
        // ขั้นตอนที่ 1: กรอกอีเมล
        $email = trim($_POST['email']);
        
        // ตรวจสอบอีเมลในฐานข้อมูล
        $stmt = $pdo->prepare("SELECT id, fullname, email FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user) {
            $verification_code = generateVerificationCode();
            
            // บันทึกรหัสยืนยันในฐานข้อมูล
            $stmt = $pdo->prepare("UPDATE users SET verification_code = ?, code_expire = DATE_ADD(NOW(), INTERVAL 5 MINUTE) WHERE id = ?");
            $stmt->execute([$verification_code, $user['id']]);
            
            // ส่งอีเมลยืนยัน
            if (sendVerificationCode($user['email'], $verification_code, $user['fullname'])) {
                $_SESSION['reset_user_id'] = $user['id'];
                $_SESSION['current_step'] = 'verify_code';
                $_SESSION['success'] = "เราได้ส่งรหัสยืนยัน 6 หลักไปยังอีเมลของคุณแล้ว";
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            } else {
                $error = "เกิดข้อผิดพลาดในการส่งอีเมล กรุณาลองอีกครั้งในภายหลัง";
            }
        } else {
            $error = "ไม่พบบัญชีผู้ใช้ที่เกี่ยวข้องกับอีเมลนี้";
        }
    } elseif (isset($_POST['verification_code'])) {
        // ขั้นตอนที่ 2: ยืนยันรหัส
        $entered_code = $_POST['verification_code'];
        $user_id = $_SESSION['reset_user_id'];
        
        // ตรวจสอบรหัสยืนยันในฐานข้อมูล
        $stmt = $pdo->prepare("SELECT id, verification_code, code_expire FROM users WHERE id = ? AND verification_code IS NOT NULL");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        
        if ($user) {
            if (strtotime($user['code_expire']) < time()) {
                $error = "รหัสยืนยันหมดอายุแล้ว กรุณาขอรหัสใหม่";
                $_SESSION['current_step'] = 'enter_email';
            } elseif ($entered_code == $user['verification_code']) {
                $_SESSION['verified'] = true;
                $_SESSION['current_step'] = 'new_password';
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            } else {
                $error = "รหัสยืนยันไม่ถูกต้อง";
            }
        } else {
            $error = "ไม่พบรหัสยืนยันที่ถูกต้อง กรุณาขอรหัสใหม่";
            $_SESSION['current_step'] = 'enter_email';
        }
    } elseif (isset($_POST['new_password']) && isset($_POST['confirm_password'])) {
        // ขั้นตอนที่ 3: ตั้งรหัสผ่านใหม่
        if ($_SESSION['verified'] && $_SESSION['current_step'] === 'new_password') {
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];
            $user_id = $_SESSION['reset_user_id'];
            
            if ($new_password !== $confirm_password) {
                $error = "รหัสผ่านไม่ตรงกัน";
            } else {
                // อัปเดตรหัสผ่านในฐานข้อมูล
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ?, verification_code = NULL, code_expire = NULL WHERE id = ?");
                $stmt->execute([$hashed_password, $user_id]);
                
                // ล้าง session
                unset($_SESSION['reset_user_id']);
                unset($_SESSION['verified']);
                unset($_SESSION['current_step']);
                
                $_SESSION['success'] = "ตั้งรหัสผ่านใหม่เรียบร้อยแล้ว คุณสามารถเข้าสู่ระบบได้ตอนนี้";
                header("Location: login.php");
                exit();
            }
        }
    }
}

// กำหนดขั้นตอนเริ่มต้น
if (!isset($_SESSION['current_step'])) {
    $_SESSION['current_step'] = 'enter_email';
}
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
        
        .recovery-header::before {
            content: "";
            position: absolute;
            top: -50px;
            right: -50px;
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
        }
        
        .recovery-header::after {
            content: "";
            position: absolute;
            bottom: -30px;
            left: -30px;
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
        }
        
        .recovery-title {
            font-family: 'Mitr', sans-serif;
            font-weight: 700;
            font-size: 1.8rem;
            margin-bottom: 10px;
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
            margin-bottom: 25px;
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
        
        .verification-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
            outline: none;
        }
        
        .resend-link {
            text-align: center;
            margin-top: 20px;
            color: #6c757d;
        }
        
        .resend-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }
        
        .resend-link a:hover {
            text-decoration: underline;
        }
        
        .login-link {
            text-align: center;
            margin-top: 20px;
        }
        
        .login-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }
        
        .login-link a:hover {
            text-decoration: underline;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .alert {
            border-radius: 12px;
            padding: 12px 20px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="recovery-container shadow-lg">
        <div class="recovery-header">
            <h2 class="recovery-title"><i class="fas fa-key"></i> กู้คืนรหัสผ่าน</h2>
        </div>
        
        <div class="recovery-body">
            <!-- แสดงขั้นตอน -->
            <div class="steps">
                <div class="step <?php echo ($_SESSION['current_step'] == 'enter_email') ? 'active' : 'completed'; ?>">
                    1
                    <span class="step-label">กรอกอีเมล</span>
                </div>
                <div class="step <?php echo ($_SESSION['current_step'] == 'verify_code') ? 'active' : (($_SESSION['current_step'] == 'new_password') ? 'completed' : ''); ?>">
                    2
                    <span class="step-label">ยืนยันรหัส</span>
                </div>
                <div class="step <?php echo ($_SESSION['current_step'] == 'new_password') ? 'active' : ''; ?>">
                    3
                    <span class="step-label">รหัสผ่านใหม่</span>
                </div>
            </div>
            
            <!-- แสดงข้อความสำเร็จ -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>
            
            <!-- แสดงข้อผิดพลาด -->
            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <!-- ขั้นตอนที่ 1: กรอกอีเมล -->
            <?php if ($_SESSION['current_step'] == 'enter_email'): ?>
                <form method="POST" action="">
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-envelope"></i> อีเมลที่สมัครสมาชิก</label>
                        <input type="email" class="form-control" name="email" required placeholder="example@domain.com">
                    </div>
                    
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-paper-plane me-2"></i> ส่งรหัสยืนยัน
                    </button>
                </form>
                
                <div class="login-link">
                    <a href="login.php"><i class="fas fa-arrow-left me-1"></i> กลับไปหน้าเข้าสู่ระบบ</a>
                </div>
            
            <!-- ขั้นตอนที่ 2: ยืนยันรหัส -->
            <?php elseif ($_SESSION['current_step'] == 'verify_code'): ?>
                <p class="text-center">เราได้ส่งรหัสยืนยัน 6 หลักไปยังอีเมลของคุณแล้ว กรุณากรอกรหัสที่ได้รับ</p>
                
                <form method="POST" action="">
                    <div class="verification-inputs">
                        <input type="text" class="verification-input" name="digit1" maxlength="1" pattern="[0-9]" required autofocus>
                        <input type="text" class="verification-input" name="digit2" maxlength="1" pattern="[0-9]" required>
                        <input type="text" class="verification-input" name="digit3" maxlength="1" pattern="[0-9]" required>
                        <input type="text" class="verification-input" name="digit4" maxlength="1" pattern="[0-9]" required>
                        <input type="text" class="verification-input" name="digit5" maxlength="1" pattern="[0-9]" required>
                        <input type="text" class="verification-input" name="digit6" maxlength="1" pattern="[0-9]" required>
                    </div>
                    
                    <input type="hidden" name="verification_code" id="fullCode">
                    
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-check-circle me-2"></i> ยืนยันรหัส
                    </button>
                </form>
                
                <div class="resend-link">
                    ไม่ได้รับรหัส? <a href="#" onclick="resendCode()">ส่งรหัสใหม่</a>
                </div>
                
                <script>
                    // รวมรหัสจากแต่ละช่องใส่ใน hidden input
                    const inputs = document.querySelectorAll('.verification-input');
                    const fullCodeInput = document.getElementById('fullCode');
                    
                    inputs.forEach((input, index) => {
                        input.addEventListener('input', function() {
                            if (this.value.length === 1 && index < inputs.length - 1) {
                                inputs[index + 1].focus();
                            }
                            updateFullCode();
                        });
                        
                        input.addEventListener('keydown', function(e) {
                            if (e.key === 'Backspace' && this.value === '' && index > 0) {
                                inputs[index - 1].focus();
                            }
                            updateFullCode();
                        });
                    });
                    
                    function updateFullCode() {
                        let code = '';
                        inputs.forEach(input => {
                            code += input.value;
                        });
                        fullCodeInput.value = code;
                    }
                    
                    // ฟังก์ชันส่งรหัสใหม่
                    function resendCode() {
                        if (confirm("ต้องการส่งรหัสยืนยันใหม่ไปยังอีเมลนี้ใช่หรือไม่?")) {
                            window.location.href = "resend_code.php";
                        }
                    }
                </script>
            
            <!-- ขั้นตอนที่ 3: ตั้งรหัสผ่านใหม่ -->
            <?php elseif ($_SESSION['current_step'] == 'new_password'): ?>
                <p class="text-center">กรุณาตั้งรหัสผ่านใหม่สำหรับบัญชีของคุณ</p>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-lock"></i> รหัสผ่านใหม่</label>
                        <input type="password" class="form-control" name="new_password" id="newPassword" required minlength="6" placeholder="รหัสผ่านใหม่">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-lock"></i> ยืนยันรหัสผ่านใหม่</label>
                        <input type="password" class="form-control" name="confirm_password" id="confirmPassword" required minlength="6" placeholder="ยืนยันรหัสผ่านใหม่">
                        <div id="passwordMatch" class="mt-2"></div>
                    </div>
                    
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-save me-2"></i> ตั้งรหัสผ่านใหม่
                    </button>
                </form>
                
                <script>
                    // ตรวจสอบว่ารหัสผ่านตรงกัน
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