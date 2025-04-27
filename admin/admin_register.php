<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Register</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: url(https://www.ditp.go.th/wp-content/uploads/2023/06/1-9.jpg);
            width: 100%;
            height: 100dvh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-size: cover;
            background-position: center;
        }
        form {
            background-color: rgba(255, 255, 255, 0.8);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body class="d-flex justify-content-center align-items-center vh-100 bg-light">

<?php
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
    <form class="p-4 shadow rounded" style="width: 350px;" action="save_admin.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <h2 class="text-center mb-4">Register</h2>
        <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" class="form-control" id="username" name="username" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <div class="mb-3">
            <label for="confirm_password" class="form-label">Confirm Password</label>
            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
        </div>
        <button type="submit" class="btn btn-success w-100">Register</button>
        <div class="text-center mt-3">
            <p>Already have an account? <a href="./admin_login.php" class="fw-bold">Login</a></p>
        </div>
    </form>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelector("form").addEventListener("submit", function (e) {
            const password = document.getElementById("password").value;
            const confirmPassword = document.getElementById("confirm_password").value;

            // ตรวจสอบความยาวรหัสผ่าน
            if (password.length < 8) {
                alert("รหัสผ่านต้องมีความยาวอย่างน้อย 8 ตัวอักษร");
                e.preventDefault();
                return;
            }

            // ตรวจสอบความซับซ้อนของรหัสผ่าน (ตัวอักษรและตัวเลขเท่านั้น)
            const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[A-Za-z\d]{8,}$/;
            if (!passwordRegex.test(password)) {
                alert("รหัสผ่านต้องประกอบด้วยตัวอักษรพิมพ์ใหญ่, ตัวอักษรพิมพ์เล็ก และตัวเลข");
                e.preventDefault();
                return;
            }

            // ตรวจสอบการยืนยันรหัสผ่าน
            if (password !== confirmPassword) {
                alert("รหัสผ่านและการยืนยันรหัสผ่านไม่ตรงกัน");
                e.preventDefault();
            }
        });
    </script>
</body>

</html>

