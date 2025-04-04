<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>คำสั่งซื้อสำเร็จ</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Prompt:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'navbar.php'; ?>
<br>
<br>
<div class="container text-center mt-5">
    <h1 class="text-success">คำสั่งซื้อของคุณถูกส่งเรียบร้อยแล้ว!</h1>
    <p class="lead">ขอบคุณที่ใช้บริการ! หมายเลขคำสั่งซื้อของคุณคือ: <strong>#<span id="order-id"></span></strong></p>

    <div class="mt-4">
        <a href="products.php" class="btn btn-primary">กลับไปเลือกซื้อสินค้า</a>
        <a href="order_status.php" class="btn btn-secondary">ตรวจสอบสถานะคำสั่งซื้อ</a>
    </div>
</div>

<script>
// รับหมายเลขคำสั่งซื้อจาก localStorage (จำลองจาก process_order.php)
document.getElementById("order-id").innerText = localStorage.getItem("lastOrderId") || "N/A";

// ล้างตะกร้า
localStorage.removeItem("cart");
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
