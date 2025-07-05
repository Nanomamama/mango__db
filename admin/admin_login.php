<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>

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

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger text-center">
            <?php echo htmlspecialchars($_GET['error'], ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <form class="p-4 shadow rounded" style="width: 350px;" method="POST" action="process_login.php">
        <h2 class="text-center mb-4">Admin Login</h2>

        <div class="mb-3">
            <label for="login" class="form-label">Username หรือ Email</label>
            <input type="text" class="form-control" id="login" name="login" placeholder="กรอก Username หรือ Email" required>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required>
        </div>

        <button type="submit" class="btn btn-primary w-100">Login</button>

        <div class="text-center mt-3">
            <p>Don't have an account? <a href="./admin_register.php" class="fw-bold">Register</a></p>
        </div>
    </form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    
</body>
</html>
