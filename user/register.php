<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สมัครสมาชิก - แบบฟอร์มทันสมัย</title>
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
            --gradient-start: #4361ee;
            --gradient-end: #3a0ca3;
            --success-dark: rgb(20, 58, 44);
            --success-end: rgba(13, 201, 132, 1);
            --danger:  #e74a3b;
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
            background: linear-gradient(to right, var(--success-end), var(--success-dark));
            clip-path: polygon(0 0, 100% 0, 100% 80%, 0 100%);
            z-index: -1;
        }

        .register-container {
            max-width: 1000px;
            width: 100%;
            margin: 0 auto;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 50px rgba(67, 238, 78, 0.25);
            animation: fadeIn 0.8s ease-out;
        }

        .register-hero {
            background: linear-gradient(to bottom right, var(--success-dark), var(--success-end));
            color: white;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .register-hero::before {
            content: "";
            position: absolute;
            top: -50px;
            right: -50px;
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
        }

        .register-hero::after {
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
            font-size: 2.8rem;
            margin-bottom: 15px;
            position: relative;
            z-index: 2;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .hero-subtitle {
            font-size: 1.2rem;
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

        .register-form {
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
            font-size: 2.2rem;
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
            font-size: 1.1rem;
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
            background: linear-gradient(to right, var(--success-end), var(--success-dark));
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
            top: 20px;
            cursor: pointer;
            color: #6c757d;
            z-index: 10;
        }

        .password-strength {
            height: 5px;
            border-radius: 10px;
            margin-top: 8px;
            background: #e9ecef;
            overflow: hidden;
            position: relative;
        }

        .strength-meter {
            height: 100%;
            width: 0;
            border-radius: 10px;
            transition: width 0.4s ease;
        }

        .password-feedback {
            font-size: 0.85rem;
            margin-top: 5px;
            height: 20px;
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
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-group {
            animation: fadeIn 0.5s ease-out forwards;
            opacity: 0;
        }

        .form-group:nth-child(1) {
            animation-delay: 0.1s;
        }

        .form-group:nth-child(2) {
            animation-delay: 0.2s;
        }

        .form-group:nth-child(3) {
            animation-delay: 0.3s;
        }

        .form-group:nth-child(4) {
            animation-delay: 0.4s;
        }

        .form-group:nth-child(5) {
            animation-delay: 0.5s;
        }

        .form-group:nth-child(6) {
            animation-delay: 0.6s;
        }

        .form-group:nth-child(7) {
            animation-delay: 0.7s;
        }

        .form-group:nth-child(8) {
            animation-delay: 0.8s;
        }

        .form-group:nth-child(9) {
            animation-delay: 0.9s;
        }

        .form-group:nth-child(10) {
            animation-delay: 1.0s;
        }

        .form-group:nth-child(11) {
            animation-delay: 1.1s;
        }

        .form-group:nth-child(12) {
            animation-delay: 1.2s;
        }

        /* Floating animation for hero section */
        @keyframes float {
            0% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-10px);
            }

            100% {
                transform: translateY(0px);
            }
        }

        .hero-icon {
            font-size: 3rem;
            margin-bottom: 20px;
            color: var(--success-end);
            animation: float 4s ease-in-out infinite;
            text-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        /* Responsive design */
        @media (max-width: 992px) {
            .register-container {
                max-width: 700px;
            }

            .hero-title {
                font-size: 2.3rem;
            }
        }

        @media (max-width: 768px) {
            .register-container {
                flex-direction: column;
            }

            .register-hero {
                padding: 30px 20px;
            }

            .register-form {
                padding: 30px 20px;
            }

            .hero-title {
                font-size: 2rem;
            }

            .form-title {
                font-size: 1.8rem;
            }
        }

        @media (max-width: 576px) {
            .hero-title {
                font-size: 1.7rem;
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
    <div class="register-container shadow-lg">
        <div class="row g-0">
            <div class="col-lg-5">
                <div class="register-hero">
                    <div class="text-center">
                        <div class="hero-icon">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <h1 class="hero-title">สมัครสมาชิกใหม่</h1>
                        <p class="hero-subtitle">เข้าร่วมชุมชนของเราเพื่อรับสิทธิพิเศษมากมายและประสบการณ์การใช้งานที่ดียิ่งขึ้น</p>
                    </div>

                    <div class="hero-features">
                        <div class="feature-item">
                            <i class="fas fa-check"></i>
                            <span>ส่วนลดพิเศษสำหรับสมาชิก</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-check"></i>
                            <span>บันทึกประวัติการใช้งาน</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-check"></i>
                            <span>ระบบแจ้งเตือนและอัปเดตข่าวสาร</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-check"></i>
                            <span>บริการลูกค้าสมาชิกพิเศษ</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="register-form">
                    <div class="form-header">
                        <h2 class="form-title">สร้างบัญชีผู้ใช้</h2>
                        <p class="form-subtitle">กรุณากรอกข้อมูลด้านล่างเพื่อสมัครสมาชิก</p>
                    </div>

                    <form action="register_save.php" method="POST" id="registrationForm">
                        <div class="form-group">
                            <label class="form-label"><i class="fas fa-user"></i> ชื่อ-นามสกุล</label>
                            <input type="text" class="form-control" name="fullname" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label"><i class="fas fa-home"></i> ที่อยู่</label>
                            <textarea class="form-control" name="address" rows="2" required placeholder="บ้าน,เลขที่,ซอย,หมู่,ถนน"></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">จังหวัด</label>
                                    <select class="form-control" name="province" id="province" required>
                                        <option value="">เลือกจังหวัด</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">อำเภอ</label>
                                    <select class="form-control" name="district" id="district" required>
                                        <option value="">เลือกอำเภอ</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">ตำบล</label>
                                    <select class="form-control" name="subdistrict" id="subdistrict" required>
                                        <option value="">เลือกตำบล</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label"><i class="fas fa-mail-bulk"></i> รหัสไปรษณีย์</label>
                            <input type="text" class="form-control" name="zipcode" id="zipcode" pattern="[0-9]{5}" maxlength="5" required placeholder="10120">
                        </div>

                        <div class="form-group">
                            <label class="form-label"><i class="fas fa-phone"></i> เบอร์โทร</label>
                            <input type="tel" class="form-control" name="phone" pattern="[0-9]{10}" maxlength="10" required placeholder="0812345678">
                        </div>

                        <div class="form-group">
                            <label class="form-label"><i class="fas fa-envelope"></i> อีเมล์</label>
                            <input type="email" class="form-control" name="email" required placeholder="example@domain.com">
                        </div>

                        <div class="form-group">
                            <label class="form-label"><i class="fas fa-lock"></i> รหัสผ่าน</label>
                            <div class="password-container">
                                <input type="password" class="form-control" name="password" id="password" required minlength="6" placeholder="อย่างน้อย 6 ตัวอักษร">
                                <i class="toggle-password fas fa-eye" id="togglePassword"></i>
                            </div>
                            <div class="password-strength mt-2">
                                <div class="strength-meter" id="strengthMeter"></div>
                            </div>
                            <div class="password-feedback" id="passwordFeedback"></div>
                        </div>

                        <div class="form-group">
                            <label class="form-label"><i class="fas fa-lock"></i> ยืนยันรหัสผ่าน</label>
                            <div class="password-container">
                                <input type="password" class="form-control" name="confirm_password" id="confirm_password" required minlength="6">
                                <i class="toggle-password fas fa-eye" id="toggleConfirmPassword"></i>
                            </div>
                            <div class="text-danger mt-2" id="password-error" style="display:none;"></div>
                        </div>

                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-submit w-100">
                                <i class="fas fa-user-plus me-2"></i> สมัครสมาชิก
                            </button>
                        </div>
                    </form>

                    <div class="form-footer">
                        <p>มีบัญชีอยู่แล้ว? <a href="member_login.php">เข้าสู่ระบบ</a></p>
                        <p class="mt-2">© 2023 ชุมชนของเรา. สงวนลิขสิทธิ์</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Password toggle visibility
        const togglePassword = document.querySelector('#togglePassword');
        const toggleConfirmPassword = document.querySelector('#toggleConfirmPassword');
        const password = document.querySelector('#password');
        const confirmPassword = document.querySelector('#confirm_password');

        togglePassword.addEventListener('click', function() {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });

        toggleConfirmPassword.addEventListener('click', function() {
            const type = confirmPassword.getAttribute('type') === 'password' ? 'text' : 'password';
            confirmPassword.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });

        // Password strength indicator
        password.addEventListener('input', function() {
            const strengthMeter = document.getElementById('strengthMeter');
            const passwordFeedback = document.getElementById('passwordFeedback');
            const value = password.value;
            let strength = 0;
            let feedback = '';

            // Check password length
            if (value.length > 7) strength += 25;
            else if (value.length > 5) strength += 10;

            // Check for uppercase letters
            if (/[A-Z]/.test(value)) strength += 25;

            // Check for lowercase letters
            if (/[a-z]/.test(value)) strength += 25;

            // Check for numbers
            if (/[0-9]/.test(value)) strength += 15;

            // Check for special characters
            if (/[^A-Za-z0-9]/.test(value)) strength += 10;

            // Update strength meter
            strengthMeter.style.width = strength + '%';

            // Set color and feedback
            if (strength < 40) {
                strengthMeter.style.background = '#dc3545';
                feedback = 'รหัสผ่านอ่อนแอ';
            } else if (strength < 70) {
                strengthMeter.style.background = '#ffc107';
                feedback = 'รหัสผ่านปานกลาง';
            } else {
                strengthMeter.style.background = '#28a745';
                feedback = 'รหัสผ่านแข็งแกร่ง';
            }

            passwordFeedback.textContent = feedback;
            passwordFeedback.style.color = strengthMeter.style.background;
        });

        // Password match validation
        function checkPasswordMatch() {
            const pass = document.getElementById('password').value;
            const confirmPass = document.getElementById('confirm_password').value;
            const errorDiv = document.getElementById('password-error');

            if (pass !== confirmPass) {
                errorDiv.style.display = 'block';
                errorDiv.textContent = 'รหัสผ่านไม่ตรงกัน';
                return false;
            }

            errorDiv.style.display = 'none';
            return true;
        }

        // Form submission handler
        document.getElementById('registrationForm').addEventListener('submit', function(e) {
            if (!checkPasswordMatch()) {
                e.preventDefault();
            } else {
                // Show loading animation on submit
                const submitBtn = this.querySelector('button[type="submit"]');
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> กำลังดำเนินการ...';
                submitBtn.disabled = true;
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            let provinceSelect = document.getElementById('province');
            let districtSelect = document.getElementById('district');
            let subdistrictSelect = document.getElementById('subdistrict');
            let provinces = [],
                amphures = [],
                tambons = [];

            Promise.all([
                fetch('../data/api_province.json').then(res => res.json()),
                fetch('../data/thai_amphures.json').then(res => res.json()),
                fetch('../data/thai_tambons.json').then(res => res.json())
            ]).then(([provinceData, amphureData, tambonData]) => {
                provinces = provinceData;
                amphures = amphureData;
                tambons = tambonData;

                provinces.forEach(item => {
                    let opt = document.createElement('option');
                    opt.value = item.id;
                    opt.textContent = item.name_th;
                    provinceSelect.appendChild(opt);
                });
            });

            provinceSelect.addEventListener('change', function() {
                let provinceId = this.value;
                districtSelect.innerHTML = '<option value="">เลือกอำเภอ</option>';
                subdistrictSelect.innerHTML = '<option value="">เลือกตำบล</option>';
                let filteredAmphures = amphures.filter(item => item.province_id == provinceId);
                filteredAmphures.forEach(item => {
                    let opt = document.createElement('option');
                    opt.value = item.id;
                    opt.textContent = item.name_th;
                    districtSelect.appendChild(opt);
                });
            });

            districtSelect.addEventListener('change', function() {
                let amphureId = this.value;
                subdistrictSelect.innerHTML = '<option value="">เลือกตำบล</option>';
                let filteredTambons = tambons.filter(item => item.amphure_id == amphureId);
                filteredTambons.forEach(item => {
                    let opt = document.createElement('option');
                    opt.value = item.id;
                    opt.textContent = item.name_th;
                    subdistrictSelect.appendChild(opt);
                });
                subdistrictSelect.onchange = function() {
                    let tambonId = this.value;
                    let tambon = tambons.find(item => item.id == tambonId);
                    if (tambon && tambon.zip_code) {
                        document.getElementById('zipcode').value = tambon.zip_code;
                    } else {
                        document.getElementById('zipcode').value = '';
                    }
                };
            });
        });

        function addOption(select, value, text) {
            const option = document.createElement('option');
            option.value = value;
            option.textContent = text;
            select.appendChild(option);
        }

        // Animation on scroll for form groups
        document.querySelectorAll('.form-group').forEach((group, index) => {
            group.style.animationDelay = `${0.1 + index * 0.1}s`;
        });
    </script>
</body>

</html>