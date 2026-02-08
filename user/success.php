<?php
require_once __DIR__ . '/../db/db.php';

if (!isset($_GET['code'])) {
    header("Location: products.php");
    exit;
}

$code = $_GET['code'];

$stmt = $conn->prepare("
    SELECT * FROM orders 
    WHERE order_code = ?
");
$stmt->bind_param("s", $code);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    echo "ไม่พบรายการสั่งซื้อ";
    exit;
}

// ดึงรายการสินค้า
$item = $conn->prepare("
    SELECT * FROM order_items 
    WHERE order_id = ?
");
$item->bind_param("i", $order['order_id']);
$item->execute();
$items = $item->get_result();
?>

<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>สั่งซื้อสำเร็จ</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
.success-box{
    max-width:700px;
    margin:auto;
    margin-top:60px;
    padding:30px;
    border-radius:16px;
    background:#f0fff4;
    box-shadow:0 0 20px rgba(0,0,0,.05);
}
</style>
</head>
<body>

<div class="success-box text-center">
    <h2 class="text-success mb-3">✅ สั่งซื้อสำเร็จ</h2>
    <h5>เลขคำสั่งซื้อ</h5>
    <h3 class="fw-bold text-primary"><?= htmlspecialchars($order['order_code']) ?></h3>

    <p class="mt-3">
        สถานะ: 
        <span class="badge bg-warning text-dark">
            <?= $order['order_status'] ?>
        </span>
    </p>

    <hr>

    <h5 class="text-start">ข้อมูลผู้สั่งซื้อ</h5>
    <p class="text-start mb-1">ชื่อ: <?= htmlspecialchars($order['customer_name']) ?></p>
    <p class="text-start mb-1">โทร: <?= htmlspecialchars($order['customer_phone']) ?></p>
    <p class="text-start mb-1">ที่อยู่: <?= nl2br(htmlspecialchars($order['customer_address'])) ?></p>
    <p class="text-start mb-1">รับสินค้าแบบ: <?= $order['receive_type'] ?></p>
    <p class="text-start mb-1">วันเวลารับ: <?= $order['receive_datetime'] ?></p>

    <hr>

    <h5 class="text-start">รายการสินค้า</h5>
    <table class="table">
        <thead>
            <tr>
                <th>สินค้า</th>
                <th>ราคา</th>
                <th>จำนวน</th>
                <th>รวม</th>
            </tr>
        </thead>
        <tbody>
        <?php 
        $total = 0;
        while($i = $items->fetch_assoc()):
            $sum = $i['price'] * $i['quantity'];
            $total += $sum;
        ?>
            <tr>
                <td><?= htmlspecialchars($i['product_name']) ?></td>
                <td><?= number_format($i['price'],2) ?></td>
                <td><?= $i['quantity'] ?></td>
                <td><?= number_format($sum,2) ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>

    <h4 class="text-end">
        รวมทั้งสิ้น: <span class="text-danger"><?= number_format($total,2) ?></span> บาท
    </h4>

    <div class="mt-4 d-flex justify-content-between">
        <a href="products.php" class="btn btn-secondary">
            ← กลับไปเลือกซื้อ
        </a>

        <button onclick="window.print()" class="btn btn-outline-primary">
            🖨 พิมพ์ใบสั่งซื้อ
        </button>
    </div>
</div>

</body>
</html>
