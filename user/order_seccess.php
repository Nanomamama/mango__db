<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>คำสั่งซื้อสำเร็จ</title>
   
</head>
<body>
<?php include 'navbar.php'; ?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<div class="container text-center mt-5">
    <h1 class="text-success">คำสั่งซื้อของคุณถูกส่งเรียบร้อยแล้ว!</h1>
    <p class="lead">ขอบคุณที่ใช้บริการ! หมายเลขคำสั่งซื้อของคุณคือ: <strong>#<span id="order-id"></span></strong></p>

    <div class="mt-4">
        <a href="products.php" class="btn btn-primary">🛍️ กลับไปเลือกซื้อสินค้า</a>
        <a href="order_status.php" class="btn btn-secondary">📦 ตรวจสอบสถานะคำสั่งซื้อ</a>
    </div>
</div>

<script>
// รับหมายเลขคำสั่งซื้อจาก localStorage (จำลองจาก process_order.php)
document.getElementById("order-id").innerText = localStorage.getItem("lastOrderId") || "N/A";

// ล้างตะกร้า
localStorage.removeItem("cart");
</script>

</body>
</html>
